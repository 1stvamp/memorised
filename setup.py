"""Installer for memorised
"""

import os
cwd = os.path.dirname(__file__)

try:
    from setuptools import setup, find_packages
except ImportError:
    from ez_setup import use_setuptools
    use_setuptools()
    from setuptools import setup, find_packages


from memorised import compat


install_requires = (
        'python-memcached>=1.58',
)


setup(
        name='memorised',
        version='1.2.0',
        author='Wes Mason',
        author_email='wes@1stvamp.org',
        description='memcache memoization decorators and utils for python',
        long_description=open(os.path.join(cwd, 'README.rst')).read(),
        url='http://github.com/1stvamp/memorised/',
        packages=find_packages(exclude=('ez_setup',)),
        install_requires=install_requires,
        license='BSD',
        classifiers=[
                'Programming Language :: Python',
                'Programming Language :: Python :: 2',
                'Programming Language :: Python :: 2.6',
                'Programming Language :: Python :: 2.7',
                'Programming Language :: Python :: 3',
                'Programming Language :: Python :: 3.2',
                'Programming Language :: Python :: 3.3',
                'Programming Language :: Python :: 3.4',
        ],
)
